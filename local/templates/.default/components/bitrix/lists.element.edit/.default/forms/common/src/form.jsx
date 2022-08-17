<<<<<<< HEAD
'use strict';

class WindowMessageWrapper extends React.Component {
  constructor(props) {
    super(props);
    this.handleMessage = this.handleMessage.bind(this);
  }
  handleMessage(event) {
      if (this.props.onWindowMessage) this.props.onWindowMessage(event);
  }
  render() {
      return this.props.children;
  }
  componentDidMount() {
      window.addEventListener("message", this.handleMessage);
  }
  componentWillUnmount() {
      window.removeEventListener("message", this.handleMessage);
  }
};


class BPForm extends React.Component {
  constructor(props) {
    super(props);

    this.state              = {};
    this.handleChange       = this.handleChange.bind(this);
    this.handleFileChange   = this.handleFileChange.bind(this);
    this.handleSubmit       = this.handleSubmit.bind(this);
    this.handleWindowMessage= this.handleWindowMessage.bind(this);
    this.form               = React.createRef();
    this.btnSubmit          = React.createRef();
    this.state.iframeSrc    = null;
    this.state.canSubmitForm= false;
    this.state.filesSigned  = false;
    
    let fields              = this.props.fields;
    Object.keys(fields).map((fieldId, i) => {
      this.state[fieldId] = fields[fieldId].value;
    });

    this.pp = BX.PopupWindowManager.create("popup-message", null, {
        content: "",
        darkMode: true,
        autoHide: true
    });
  }
  render() {
    let textblocks = Object.keys(this.props.fields)
                          .map(fieldId => this.props.fields[fieldId])
                          .filter(field => field.type == "textblock");
    
    return (
      <WindowMessageWrapper onWindowMessage={this.handleWindowMessage}>
        <form name={this.props.formName} onSubmit={this.handleSubmit} action={this.props.formAction} method="POST" encType="multipart/form-data" ref={this.form}>
        {Object.keys(this.props.fields).map((fieldId, i) => {
            let field = {
              ...this.props.fields[fieldId],
              value: this.state[fieldId]
            };
            field.show = this.showField(fieldId);

            if(field.type == "hidden"){
              return <input type="hidden" key={field.id} data-id={field.id} name={field.name} value={field.value} hidden={!field.show}/>
            }
            if(field.type == "treelist"){
              return <FormControlTreeSelect key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "list"){
              return <FormControlSelect key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "file"){
              return <FormControlFile key={field.id} {...field} handleChange={this.handleFileChange}/>
            }
            if(field.type == "filemultiple"){
              return <FormControlFileMultiple key={field.id} {...field} handleChange={this.handleFileChange}/>
            }
            if(field.type == "date"){
              return <FormControlDate key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "datetime"){
              return <FormControlDateTime key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "datetimemultiple"){
              return <FormControlDateTimeMultiple key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "user"){
              return <FormControlUser key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "textarea"){
              return <FormControlTextarea key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "bool"){
              return <FormControlBool key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "table"){
              return <FormControlTable key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "textblock"){
              return null;
            }
            return <FormControlText key={field.id} {...field} handleChange={this.handleChange}/>
          })}
          {textblocks.length
            ? <div className="alert alert-warning">
              {textblocks.map(field => 
                <div key={field.id} hidden={!this.showField(field.id)} dangerouslySetInnerHTML={{__html: field.description}}></div>
              )}
            </div>
            : null
          }
          <p className="font-weight-bolder">* Поле является обязательным</p>
          <button type="submit" className="btn btn-success submit_btn" ref={this.btnSubmit}><span dangerouslySetInnerHTML={{__html: this.props.submitText?this.props.submitText:"Продолжить &rarr;"}}></span></button>
        </form>
        {this.state.iframeSrc? <iframe src={this.state.iframeSrc} className="popup-iframe"/> : null}
      </WindowMessageWrapper>
    );
  }
  showField(fieldId){
    const field = this.props.fields[fieldId];
    return typeof field.show == "function"
                  ? field.show.call(this)
                  : field.show;
  }
  onErrror(message){
    this.pp.setContent(message);
    this.pp.show();
  }
  getValue(fieldId) {
    return this.state[fieldId];
  }
  getValueXmlId(fieldId, value = null) {
    let field = this.props.fields[fieldId];
    value = value || this.state[fieldId];

    return value
              ? field.values[value].XML_ID
              : null;
  }
  handleFileChange({target}) {
    const fieldId   = target.getAttribute('data-id');
    const fieldVal  = target.value;
    this.setState({
      [fieldId]: fieldVal?1:0
    });
    
  }
  handleChange({target}) {
    const fieldId   = target.getAttribute('data-id');
    const fieldVal  = target.value;

    this.setState({
      [fieldId]: fieldVal
    });
  }
  handleSubmit(e) {
    if(this.state.canSubmitForm) return;
    e.preventDefault();
    this.btnSubmit.current.setAttribute('disabled', "disabled");
    
    let request = null;
    let user_blocks = Object.keys(this.props.fields).filter(fieldId => this.props.fields[fieldId].type == "user");
    if(typeof this.props.submitDataType == "undefined" || this.props.submitDataType == "json"){
      let request_vals = {...this.state};
      user_blocks.forEach(fieldId => {
        if(!request_vals[fieldId]) return;
        request_vals[fieldId] = (request_vals[fieldId].match(/(\d+)/ig) || []).join(',');
      });
      request = JSON.stringify(request_vals);
    }else{
      request = new FormData();
      e.target.querySelectorAll('select, textarea, input[type=text], input[type=date], input[type=number], input[type=email], input[type=hidden], input[type=checkbox]:checked').forEach((field) => {
        let val   = field.value;
        let name  = field.getAttribute('data-id') || field.getAttribute('name');
        if(!name) return;
        if(~user_blocks.indexOf(name)){
          val = (val.match(/(\d+)/ig) || []).join(',');
        }
        request.set(name, val);
      });
      e.target.querySelectorAll('input[type=file]').forEach((field) => {
        if(!field.files.length) return;
        request.append(field.getAttribute('data-id') || field.getAttribute('name'),  field.files[0], field.files[0].name);
      });
    }

    fetch(this.props.formAjax, {method: 'POST', body: request, credentials: 'include'})
      .then(resp => resp.json())
      .then(resp => {
        if(resp.status == "ERROR"){
            this.onErrror(resp.status_message);
            return;
        }
        
        this.setState(resp.data.fields, ()=>{
            if(resp.status == "OK"){
              if(typeof resp.alert != "undefined" && resp.alert){
                this.onErrror(resp.alert);
              }
              this.setState({canSubmitForm: true}, () => {
                setTimeout(()=>{
                  this.form.current.submit();//dispatchEvent(new Event('submit'))
                }, 3000);
              });
            }else if(resp.status == "REDIRECT"){
              this.setState({
                iframeSrc: resp.data.location
              });
            }
        })
      }).catch((error)=>{
        console.error(error);
        this.onErrror(error);
      }).finally(()=>{
        this.btnSubmit.current.removeAttribute('disabled');
      });
  }
  handleWindowMessage(event){
    if (!window.location.origin) {
      window.location.origin = window.location.protocol + "//" 
          + window.location.hostname 
          + (window.location.port ? ':' + window.location.port : '');
    }
    if (event.origin !== window.location.origin){
      return;
    }
    if(event.data == "filesigner_hiden"){
      this.setState({
        iframeSrc: null
      });
    }
    if(event.data == "filesigner_signed"){
      this.setState({
        filesSigned: true,
        iframeSrc: null
      }, () => { this.form.current.submit() });//dispatchEvent(new Event('submit'))
    }
    if(event.data == "filesigner_error"){
        this.onErrror("Ошибка. Попробуйте позже");
    }
  }
}

class FormControlUser extends React.Component{
  constructor(props) {
    super(props);
    this.form_group__input      = React.createRef();
    this.form_group__ussel      = React.createRef();
    this.onChange               = this.onChange.bind(this);
    this.userSelector           = null;
  }
  onChange(employees){
    let val = [];
    for(let key in employees){
      let employee = employees[key];
      val.push(`${employee.name} [${employee.id}]`);
    }
    this.form_group__input.current.value = val.join(', ');
    this.props.handleChange({target:this.form_group__input.current})
  }
  componentWillUnmount() {
    if(!this.userSelector) return;
    BX.removeCustomEvent(this.userSelector, 'on-change', this.onChange);
  }
  componentDidMount() {
    this.form_group__ussel.current.querySelectorAll('script').forEach((node) => {
      var script = document.createElement('script');
      var varname= null;
      script.innerHTML = node.innerHTML;
      
      if(node.getAttribute('src')){
        script.setAttribute('src', node.getAttribute('src'));
      }else{
        varname = node.innerHTML.match(/var (.*?) = new IntranetUsers/);
      }
      document.body.appendChild(script)
      if(!!varname){
        setTimeout(() => {
          this.userSelector = window[varname.pop().trim()];
          BX.addCustomEvent(this.userSelector, 'on-change', this.onChange);
        }, 500);
      }
    })
  }
  render(){
    const {id, show, title, value, description, custom} = this.props;
    return (
      <div className="form-group form-group-userselector" hidden={!show}>
        <label>{title}</label>
        <input type="text" className="form-control" data-id={id} name={id+"_val"} defaultValue={this.value} ref={this.form_group__input}/>
        <div dangerouslySetInnerHTML={{__html: custom}} className="userselector" ref={this.form_group__ussel}></div>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlDate extends React.Component{
  constructor(props) {
    super(props);
    this.onClick= this.onClick.bind(this);
    this.input  = React.createRef();
  }
  onClick({target}){
    BX.calendar({node: target, field: target, bTime: false, callback: this.props.callback || null});
  }
  componentWillUnmount() {
    BX.unbind(BX(this.input.current), 'change', this.props.handleChange);
  }
  componentDidMount(){
    BX.bind(BX(this.input.current), 'change', this.props.handleChange);
  }
  render(){
    const {id, show, title, name, value, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <input className="form-control" name={name} type="text" data-id={id} value={value} placeholder={placeholder} onChange={handleChange} onClick={this.onClick} ref={this.input}></input>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlDateTime extends FormControlDate{
  constructor(props) {
    super(props);
    this.onClick= this.onClick.bind(this);
  }
  onClick({target}){
    BX.calendar({node: target, field: target, bTime: true, callback: this.props.callback || null});
  }
}
class FormControlTextarea extends React.Component{
  render(){
    const {id, show, type, title, name, value, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <textarea className="form-control" name={name} type={type === 'readonly' ? 'text' : type} data-id={id} value={value} placeholder={placeholder} onChange={handleChange} readOnly={type == "readonly"}></textarea>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlFile extends React.Component{
  render(){
    const {id, show, title, name, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <input className="form-control" name={name} type={this.props.type} data-id={id} placeholder={placeholder} onChange={handleChange}></input>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlText extends React.Component{
  render(){
    const {id, show, type, title, name, value, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <input className="form-control" name={name} type={type === 'readonly' ? 'text' : type} data-id={id} value={value} placeholder={placeholder} onChange={handleChange} readOnly={type == "readonly"}></input>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlSelect extends React.Component{
  constructor(props) {
    super(props);
    this.isMultiple = ~this.props.name.indexOf('[]');
    this.onChange   = this.onChange.bind(this);
  }
  onChange(e){
    let options = e.target.querySelectorAll('option:checked');
    
    this.props.handleChange({
      target:{
        getAttribute: name => e.target.getAttribute(name),
        value: this.isMultiple
                ? [...options].map(option => option.value)
                : e.target.value
      }
    });
  }
  render(){
    const {id, show, title, name, value, values, description, handleChange} = this.props;
    let vals = Object.keys(values)
                .map(key => values[key])
                .sort((a, b) => a.SORT > b.SORT);

    let curValue = value;
    if(this.isMultiple && !!curValue && typeof curValue != "object"){
      curValue = [curValue];
    }
    return (
        <div className="form-group" hidden={!show}>
          <label>{title}</label>
          <select className="form-control" name={name} data-id={id} value={curValue} onChange={this.onChange} multiple={this.isMultiple}>
            {vals.map(val => {
              return <option key={val.XML_ID} data-id={val.XML_ID} value={val.ID}>{val.VALUE}</option>
            })}
          </select>
          <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
        </div>
    )
  }
}


const FormControlFileMultiple = (props) => {
  const {id, show, title, name, placeholder, description, handleChange} = props;
  const [fieldsCount, setFieldsCount] = React.useState(1);

  let fields = [];
  for(let i = 0; i < fieldsCount; i++){
    fields.push(
      <input key={`${id}_${i}`} className="form-control mb-2" name={`${name}[n${i}][VALUE]`} type="file" data-id={`${id}_${i}`} placeholder={placeholder}></input>
    )
  }
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      {fields}
      <div className="mb-3"><button className="btn btn-sm btn-secondary" type="button" onClick={() => setFieldsCount(fieldsCount + 1)}>Добавить файл</button></div>
      <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
    </div>
  )
};
const FormControlTreeSelect = (props) => {
  const {id, show, title, name, value, values, description, handleChange} = props;

  const getParent = curValue => {
    let items = [];
    if(curValue && values[curValue].PARENT){
      items.push(+values[curValue].PARENT);
      items = items.concat(getParent(values[curValue].PARENT))
    }

    return items;
  }
  const renderOptions = items => {
    return items.map(val => {
      return <option key={val.XML_ID} data-id={val.XML_ID} value={val.ID}>{val.VALUE}</option>
    });
  };

  const onChage = (e) => {
    if(+e.target.value){
      handleChange(e);
    }
  };
  
  const valueParent = value ? values[value].PARENT : null;
  const sortedValues = Object.keys(values)
                  .map(key => values[key])
                  .sort((a, b) => a.SORT > b.SORT);
  
  const childOptions = sortedValues.filter(val => val.PARENT == value);
  const parents= getParent(value).reverse();
  
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      {parents.map(item => {
        const itemOb = values[item];
        return (
          <select key={`select_parent_${itemOb.ID}`} className="form-control mb-3" data-id={id} value={itemOb.ID} onChange={onChage}>
            {renderOptions(sortedValues.filter(val => val.PARENT == itemOb.PARENT))}
          </select>
        )
      })}
      <select key={`select_${id}`} className="form-control mb-3" name={name} data-id={id} value={value} onChange={onChage}>
        <option value="0">Выбрать</option>
        {renderOptions(sortedValues.filter(val => val.PARENT == valueParent))}
      </select>
      {childOptions.length
        ? (
          <select key={`select_child_${id}`} className="form-control" data-id={id} value={"0"} onChange={onChage}>
            <option value="0">Выбрать</option>
            {renderOptions(childOptions)}
          </select>
        )
        : null
      }
      <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
    </div>
  )
}

const FormControlBool = props => {
  const {id, value, values, show, title, description, handleChange} = props;
  const input = React.useRef(value);
  const onClick = e => {
    input.current.value = +value ?0 :1;
    handleChange({...e, target:input.current})
  };
  return (
    
    <div className="form-group" hidden={!show}>
      <input type="hidden" data-id={id}  ref={input}/>
      <button type="button" className="btn btn-primary btn-sm" onClick={onClick}>{values[value]}</button>
      <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
    </div>
  )
}
const FormControlDateTimeMultiple = props => {
  const {id, value, title, show, name, handleChange} = props;
  const [fieldsCount, setFieldsCount] = React.useState(1);
  let fields = [];
  let curValue = (Array.isArray(value) && value) || [];

  const setValue = (i, value) => {
    curValue[i] = value;
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: curValue
    }})
  }
  const deleteValue = i => {
    curValue.splice(i, 1);
    setFieldsCount(fieldsCount - 1);
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: curValue
    }})
  }
  for(let i = 0; i < fieldsCount; i++){
    fields.push(
      <div key={`${props.id}_${i}`} className="row">
        <div className="col-md-11">
          <FormControlDateTime {...props} value={curValue[i] || ''} title={`${title} #${i+1}`} id={`${id}[${i}]`} name={`${name}[n${i}][VALUE]`} handleChange={ e => { setValue(i, e.target.value) }}/>
        </div>
        <div className="col-md-1">
          <label style={{opacity:0}}>Удалить</label>
          <button type="button" className="btn btn-sm btn-block btn-danger" onClick={ e => { deleteValue(i); }}>&times;</button>
        </div>
      </div>
    );
  }
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      <div className="card">
        <div className="card-body">
          {fields}
        </div>
        <div className="card-footer">
          <button type="button" className="btn btn-primary btn-sm" onClick={ e => {setFieldsCount(fieldsCount+1);}}>Добавить</button>
        </div>
      </div>
    </div>
  );
}
const FormControlTable = props => {
  const {id, name, value, title, table, show, handleChange} = props;
  const [rowsCount, setRowsCount] = React.useState(1);
  const mapValueToCurvValue = props.mapValueToCurvValue || (value => {
    let vals = [];
    if(Array.isArray(value)){
      value.forEach(val => {
        vals.push(val.split(', '));
      });
    }
    return vals;
  });
  const mapCurValueToValue = props.mapCurValueToValue || (values => {
    let vals = [];
    values.forEach(val => {
      vals.push(val.join(', '));
    });
    return vals;
  });
  const setCurValueItem = (i, j, value) => {
    if(typeof curValue[i] === 'undefined'){
      curValue[i] = [];
    }
    curValue[i][j] = value;
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: mapCurValueToValue(curValue)
    }})
  };
  const removCurValueItem = i => {
    if(rowsCount == 1) return;
    curValue.splice(i, 1);
    setRowsCount(rowsCount - 1);
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: mapCurValueToValue(curValue)
    }})
  };
  const curValue = mapValueToCurvValue(value);
  let rows = [];
  for(let i = 0; i < rowsCount; i++){
    rows.push(
      <div key={`${id}_row_${i}`} className="row mb-2">
        <div className="col-md-11">
          <div className="row">
            {table.columns.map((item, j) => {
              return (<div key={`${id}_row_${i}_col_${j}`} className={'col-md-' + (12 / table.columns.length)}>
                <input type="text" className="form-control" value={(curValue[i] && curValue[i][j]) || ''} onChange={ e => setCurValueItem(i, j, e.target.value)}/>
              </div>)
            })}
          </div>
        </div>
        <div className="col-md-1">
          <button type="button" className="btn btn-sm btn-block btn-danger" onClick={ e => { removCurValueItem(i); }}>&times;</button>
        </div>
      </div>
    )
  }
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      <div className="card">
        <div className="card-body">
          <div className="row mb-3">
            <div className="col-md-11">
              <div className="row">
                {table.columns.map((item, i) => {
                  return <div key={`${id}_header_${item.id}`} className={'col-md-' + (12 / table.columns.length)}><strong>{item.title}</strong></div>
                })}
              </div>
            </div>
            <div className="col-md-1"></div>
          </div>
          {rows}
          {Array.isArray(value) && value.map((valueItem, i) => {
            return <input key={`${id}_value_${i}`} type="hidden" data-id={`${id}[${i}]`} name={`${name}[n${i}][VALUE]`} value={valueItem}/>
          })}
        </div>
        <div className="card-footer">
          <button type="button" className="btn btn-primary btn-sm" onClick={ e => {setRowsCount(rowsCount+1);}}>Добавить</button>
        </div>
      </div>
    </div>
  );
=======
'use strict';

class WindowMessageWrapper extends React.Component {
  constructor(props) {
    super(props);
    this.handleMessage = this.handleMessage.bind(this);
  }
  handleMessage(event) {
      if (this.props.onWindowMessage) this.props.onWindowMessage(event);
  }
  render() {
      return this.props.children;
  }
  componentDidMount() {
      window.addEventListener("message", this.handleMessage);
  }
  componentWillUnmount() {
      window.removeEventListener("message", this.handleMessage);
  }
};


class BPForm extends React.Component {
  constructor(props) {
    super(props);

    this.state              = {};
    this.handleChange       = this.handleChange.bind(this);
    this.handleFileChange   = this.handleFileChange.bind(this);
    this.handleSubmit       = this.handleSubmit.bind(this);
    this.handleWindowMessage= this.handleWindowMessage.bind(this);
    this.form               = React.createRef();
    this.btnSubmit          = React.createRef();
    this.state.iframeSrc    = null;
    this.state.canSubmitForm= false;
    this.state.filesSigned  = false;
    
    let fields              = this.props.fields;
    Object.keys(fields).map((fieldId, i) => {
      this.state[fieldId] = fields[fieldId].value;
    });

    this.pp = BX.PopupWindowManager.create("popup-message", null, {
        content: "",
        darkMode: true,
        autoHide: true
    });
  }
  render() {
    let textblocks = Object.keys(this.props.fields)
                          .map(fieldId => this.props.fields[fieldId])
                          .filter(field => field.type == "textblock");
    
    return (
      <WindowMessageWrapper onWindowMessage={this.handleWindowMessage}>
        <form name={this.props.formName} onSubmit={this.handleSubmit} action={this.props.formAction} method="POST" encType="multipart/form-data" ref={this.form}>
        {Object.keys(this.props.fields).map((fieldId, i) => {
            let field = {
              ...this.props.fields[fieldId],
              value: this.state[fieldId]
            };
            field.show = this.showField(fieldId);

            if(field.type == "hidden"){
              return <input type="hidden" key={field.id} data-id={field.id} name={field.name} value={field.value} hidden={!field.show}/>
            }
            if(field.type == "treelist"){
              return <FormControlTreeSelect key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "list"){
              return <FormControlSelect key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "file"){
              return <FormControlFile key={field.id} {...field} handleChange={this.handleFileChange}/>
            }
            if(field.type == "filemultiple"){
              return <FormControlFileMultiple key={field.id} {...field} handleChange={this.handleFileChange}/>
            }
            if(field.type == "date"){
              return <FormControlDate key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "datetime"){
              return <FormControlDateTime key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "datetimemultiple"){
              return <FormControlDateTimeMultiple key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "user"){
              return <FormControlUser key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "textarea"){
              return <FormControlTextarea key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "bool"){
              return <FormControlBool key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "table"){
              return <FormControlTable key={field.id} {...field} handleChange={this.handleChange}/>
            }
            if(field.type == "textblock"){
              return null;
            }
            return <FormControlText key={field.id} {...field} handleChange={this.handleChange}/>
          })}
          {textblocks.length
            ? <div className="alert alert-warning">
              {textblocks.map(field => 
                <div key={field.id} hidden={!this.showField(field.id)} dangerouslySetInnerHTML={{__html: field.description}}></div>
              )}
            </div>
            : null
          }
          <p className="font-weight-bolder">* Поле является обязательным</p>
          <button type="submit" className="btn btn-success submit_btn" ref={this.btnSubmit}><span dangerouslySetInnerHTML={{__html: this.props.submitText?this.props.submitText:"Продолжить &rarr;"}}></span></button>
        </form>
        {this.state.iframeSrc? <iframe src={this.state.iframeSrc} className="popup-iframe"/> : null}
      </WindowMessageWrapper>
    );
  }
  showField(fieldId){
    const field = this.props.fields[fieldId];
    return typeof field.show == "function"
                  ? field.show.call(this)
                  : field.show;
  }
  onErrror(message){
    this.pp.setContent(message);
    this.pp.show();
  }
  getValue(fieldId) {
    return this.state[fieldId];
  }
  getValueXmlId(fieldId, value = null) {
    let field = this.props.fields[fieldId];
    value = value || this.state[fieldId];

    return value
              ? field.values[value].XML_ID
              : null;
  }
  handleFileChange({target}) {
    const fieldId   = target.getAttribute('data-id');
    const fieldVal  = target.value;
    this.setState({
      [fieldId]: fieldVal?1:0
    });
    
  }
  handleChange({target}) {
    const fieldId   = target.getAttribute('data-id');
    const fieldVal  = target.value;

    this.setState({
      [fieldId]: fieldVal
    });
  }
  handleSubmit(e) {
    if(this.state.canSubmitForm) return;
    e.preventDefault();
    this.btnSubmit.current.setAttribute('disabled', "disabled");
    
    let request = null;
    let user_blocks = Object.keys(this.props.fields).filter(fieldId => this.props.fields[fieldId].type == "user");
    if(typeof this.props.submitDataType == "undefined" || this.props.submitDataType == "json"){
      let request_vals = {...this.state};
      user_blocks.forEach(fieldId => {
        if(!request_vals[fieldId]) return;
        request_vals[fieldId] = (request_vals[fieldId].match(/(\d+)/ig) || []).join(',');
      });
      request = JSON.stringify(request_vals);
    }else{
      request = new FormData();
      e.target.querySelectorAll('select, textarea, input[type=text], input[type=date], input[type=number], input[type=email], input[type=hidden], input[type=checkbox]:checked').forEach((field) => {
        let val   = field.value;
        let name  = field.getAttribute('data-id') || field.getAttribute('name');
        if(!name) return;
        if(~user_blocks.indexOf(name)){
          val = (val.match(/(\d+)/ig) || []).join(',');
        }
        request.set(name, val);
      });
      e.target.querySelectorAll('input[type=file]').forEach((field) => {
        if(!field.files.length) return;
        request.append(field.getAttribute('data-id') || field.getAttribute('name'),  field.files[0], field.files[0].name);
      });
    }

    fetch(this.props.formAjax, {method: 'POST', body: request, credentials: 'include'})
      .then(resp => resp.json())
      .then(resp => {
        if(resp.status == "ERROR"){
            this.onErrror(resp.status_message);
            return;
        }
        
        this.setState(resp.data.fields, ()=>{
            if(resp.status == "OK"){
              if(typeof resp.alert != "undefined" && resp.alert){
                this.onErrror(resp.alert);
              }
              this.setState({canSubmitForm: true}, () => {
                setTimeout(()=>{
                  this.form.current.submit();//dispatchEvent(new Event('submit'))
                }, 3000);
              });
            }else if(resp.status == "REDIRECT"){
              this.setState({
                iframeSrc: resp.data.location
              });
            }
        })
      }).catch((error)=>{
        console.error(error);
        this.onErrror(error);
      }).finally(()=>{
        this.btnSubmit.current.removeAttribute('disabled');
      });
  }
  handleWindowMessage(event){
    if (!window.location.origin) {
      window.location.origin = window.location.protocol + "//" 
          + window.location.hostname 
          + (window.location.port ? ':' + window.location.port : '');
    }
    if (event.origin !== window.location.origin){
      return;
    }
    if(event.data == "filesigner_hiden"){
      this.setState({
        iframeSrc: null
      });
    }
    if(event.data == "filesigner_signed"){
      this.setState({
        filesSigned: true,
        iframeSrc: null
      }, () => { this.form.current.submit() });//dispatchEvent(new Event('submit'))
    }
    if(event.data == "filesigner_error"){
        this.onErrror("Ошибка. Попробуйте позже");
    }
  }
}

class FormControlUser extends React.Component{
  constructor(props) {
    super(props);
    this.form_group__input      = React.createRef();
    this.form_group__ussel      = React.createRef();
    this.onChange               = this.onChange.bind(this);
    this.userSelector           = null;
  }
  onChange(employees){
    let val = [];
    for(let key in employees){
      let employee = employees[key];
      val.push(`${employee.name} [${employee.id}]`);
    }
    this.form_group__input.current.value = val.join(', ');
    this.props.handleChange({target:this.form_group__input.current})
  }
  componentWillUnmount() {
    if(!this.userSelector) return;
    BX.removeCustomEvent(this.userSelector, 'on-change', this.onChange);
  }
  componentDidMount() {
    this.form_group__ussel.current.querySelectorAll('script').forEach((node) => {
      var script = document.createElement('script');
      var varname= null;
      script.innerHTML = node.innerHTML;
      
      if(node.getAttribute('src')){
        script.setAttribute('src', node.getAttribute('src'));
      }else{
        varname = node.innerHTML.match(/var (.*?) = new IntranetUsers/);
      }
      document.body.appendChild(script)
      if(!!varname){
        setTimeout(() => {
          this.userSelector = window[varname.pop().trim()];
          BX.addCustomEvent(this.userSelector, 'on-change', this.onChange);
        }, 500);
      }
    })
  }
  render(){
    const {id, show, title, value, description, custom} = this.props;
    return (
      <div className="form-group form-group-userselector" hidden={!show}>
        <label>{title}</label>
        <input type="text" className="form-control" data-id={id} name={id+"_val"} defaultValue={this.value} ref={this.form_group__input}/>
        <div dangerouslySetInnerHTML={{__html: custom}} className="userselector" ref={this.form_group__ussel}></div>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlDate extends React.Component{
  constructor(props) {
    super(props);
    this.onClick= this.onClick.bind(this);
    this.input  = React.createRef();
  }
  onClick({target}){
    BX.calendar({node: target, field: target, bTime: false, callback: this.props.callback || null});
  }
  componentWillUnmount() {
    BX.unbind(BX(this.input.current), 'change', this.props.handleChange);
  }
  componentDidMount(){
    BX.bind(BX(this.input.current), 'change', this.props.handleChange);
  }
  render(){
    const {id, show, title, name, value, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <input className="form-control" name={name} type="text" data-id={id} value={value} placeholder={placeholder} onChange={handleChange} onClick={this.onClick} ref={this.input}></input>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlDateTime extends FormControlDate{
  constructor(props) {
    super(props);
    this.onClick= this.onClick.bind(this);
  }
  onClick({target}){
    BX.calendar({node: target, field: target, bTime: true, callback: this.props.callback || null});
  }
}
class FormControlTextarea extends React.Component{
  render(){
    const {id, show, type, title, name, value, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <textarea className="form-control" name={name} type={type === 'readonly' ? 'text' : type} data-id={id} value={value} placeholder={placeholder} onChange={handleChange} readOnly={type == "readonly"}></textarea>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlFile extends React.Component{
  render(){
    const {id, show, title, name, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <input className="form-control" name={name} type={this.props.type} data-id={id} placeholder={placeholder} onChange={handleChange}></input>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlText extends React.Component{
  render(){
    const {id, show, type, title, name, value, placeholder, description, handleChange} = this.props;
    return (
      <div className="form-group" hidden={!show}>
        <label>{title}</label>
        <input className="form-control" name={name} type={type === 'readonly' ? 'text' : type} data-id={id} value={value} placeholder={placeholder} onChange={handleChange} readOnly={type == "readonly"}></input>
        <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
      </div>
    )
  }
}
class FormControlSelect extends React.Component{
  constructor(props) {
    super(props);
    this.isMultiple = ~this.props.name.indexOf('[]');
    this.onChange   = this.onChange.bind(this);
  }
  onChange(e){
    let options = e.target.querySelectorAll('option:checked');
    
    this.props.handleChange({
      target:{
        getAttribute: name => e.target.getAttribute(name),
        value: this.isMultiple
                ? [...options].map(option => option.value)
                : e.target.value
      }
    });
  }
  render(){
    const {id, show, title, name, value, values, description, handleChange} = this.props;
    let vals = Object.keys(values)
                .map(key => values[key])
                .sort((a, b) => a.SORT > b.SORT);

    let curValue = value;
    if(this.isMultiple && !!curValue && typeof curValue != "object"){
      curValue = [curValue];
    }
    return (
        <div className="form-group" hidden={!show}>
          <label>{title}</label>
          <select className="form-control" name={name} data-id={id} value={curValue} onChange={this.onChange} multiple={this.isMultiple}>
            {vals.map(val => {
              return <option key={val.XML_ID} data-id={val.XML_ID} value={val.ID}>{val.VALUE}</option>
            })}
          </select>
          <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
        </div>
    )
  }
}


const FormControlFileMultiple = (props) => {
  const {id, show, title, name, placeholder, description, handleChange} = props;
  const [fieldsCount, setFieldsCount] = React.useState(1);

  let fields = [];
  for(let i = 0; i < fieldsCount; i++){
    fields.push(
      <input key={`${id}_${i}`} className="form-control mb-2" name={`${name}[n${i}][VALUE]`} type="file" data-id={`${id}_${i}`} placeholder={placeholder}></input>
    )
  }
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      {fields}
      <div className="mb-3"><button className="btn btn-sm btn-secondary" type="button" onClick={() => setFieldsCount(fieldsCount + 1)}>Добавить файл</button></div>
      <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
    </div>
  )
};
const FormControlTreeSelect = (props) => {
  const {id, show, title, name, value, values, description, handleChange} = props;

  const getParent = curValue => {
    let items = [];
    if(curValue && values[curValue].PARENT){
      items.push(+values[curValue].PARENT);
      items = items.concat(getParent(values[curValue].PARENT))
    }

    return items;
  }
  const renderOptions = items => {
    return items.map(val => {
      return <option key={val.XML_ID} data-id={val.XML_ID} value={val.ID}>{val.VALUE}</option>
    });
  };

  const onChage = (e) => {
    if(+e.target.value){
      handleChange(e);
    }
  };
  
  const valueParent = value ? values[value].PARENT : null;
  const sortedValues = Object.keys(values)
                  .map(key => values[key])
                  .sort((a, b) => a.SORT > b.SORT);
  
  const childOptions = sortedValues.filter(val => val.PARENT == value);
  const parents= getParent(value).reverse();
  
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      {parents.map(item => {
        const itemOb = values[item];
        return (
          <select key={`select_parent_${itemOb.ID}`} className="form-control mb-3" data-id={id} value={itemOb.ID} onChange={onChage}>
            {renderOptions(sortedValues.filter(val => val.PARENT == itemOb.PARENT))}
          </select>
        )
      })}
      <select key={`select_${id}`} className="form-control mb-3" name={name} data-id={id} value={value} onChange={onChage}>
        <option value="0">Выбрать</option>
        {renderOptions(sortedValues.filter(val => val.PARENT == valueParent))}
      </select>
      {childOptions.length
        ? (
          <select key={`select_child_${id}`} className="form-control" data-id={id} value={"0"} onChange={onChage}>
            <option value="0">Выбрать</option>
            {renderOptions(childOptions)}
          </select>
        )
        : null
      }
      <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
    </div>
  )
}

const FormControlBool = props => {
  const {id, value, values, show, title, description, handleChange} = props;
  const input = React.useRef(value);
  const onClick = e => {
    input.current.value = +value ?0 :1;
    handleChange({...e, target:input.current})
  };
  return (
    
    <div className="form-group" hidden={!show}>
      <input type="hidden" data-id={id}  ref={input}/>
      <button type="button" className="btn btn-primary btn-sm" onClick={onClick}>{values[value]}</button>
      <div className="form-text alert alert-secondary py-1 px-3" hidden={description == ""} dangerouslySetInnerHTML={{__html: description}}></div>
    </div>
  )
}
const FormControlDateTimeMultiple = props => {
  const {id, value, title, show, name, handleChange} = props;
  const [fieldsCount, setFieldsCount] = React.useState(1);
  let fields = [];
  let curValue = (Array.isArray(value) && value) || [];

  const setValue = (i, value) => {
    curValue[i] = value;
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: curValue
    }})
  }
  const deleteValue = i => {
    curValue.splice(i, 1);
    setFieldsCount(fieldsCount - 1);
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: curValue
    }})
  }
  for(let i = 0; i < fieldsCount; i++){
    fields.push(
      <div key={`${props.id}_${i}`} className="row">
        <div className="col-md-11">
          <FormControlDateTime {...props} value={curValue[i] || ''} title={`${title} #${i+1}`} id={`${id}[${i}]`} name={`${name}[n${i}][VALUE]`} handleChange={ e => { setValue(i, e.target.value) }}/>
        </div>
        <div className="col-md-1">
          <label style={{opacity:0}}>Удалить</label>
          <button type="button" className="btn btn-sm btn-block btn-danger" onClick={ e => { deleteValue(i); }}>&times;</button>
        </div>
      </div>
    );
  }
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      <div className="card">
        <div className="card-body">
          {fields}
        </div>
        <div className="card-footer">
          <button type="button" className="btn btn-primary btn-sm" onClick={ e => {setFieldsCount(fieldsCount+1);}}>Добавить</button>
        </div>
      </div>
    </div>
  );
}
const FormControlTable = props => {
  const {id, name, value, title, table, show, handleChange} = props;
  const [rowsCount, setRowsCount] = React.useState(1);
  const mapValueToCurvValue = props.mapValueToCurvValue || (value => {
    let vals = [];
    if(Array.isArray(value)){
      value.forEach(val => {
        vals.push(val.split(', '));
      });
    }
    return vals;
  });
  const mapCurValueToValue = props.mapCurValueToValue || (values => {
    let vals = [];
    values.forEach(val => {
      vals.push(val.join(', '));
    });
    return vals;
  });
  const setCurValueItem = (i, j, value) => {
    if(typeof curValue[i] === 'undefined'){
      curValue[i] = [];
    }
    curValue[i][j] = value;
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: mapCurValueToValue(curValue)
    }})
  };
  const removCurValueItem = i => {
    if(rowsCount == 1) return;
    curValue.splice(i, 1);
    setRowsCount(rowsCount - 1);
    handleChange({target: {
      getAttribute: name => name == 'data-id'? id: null,
      value: mapCurValueToValue(curValue)
    }})
  };
  const curValue = mapValueToCurvValue(value);
  let rows = [];
  for(let i = 0; i < rowsCount; i++){
    rows.push(
      <div key={`${id}_row_${i}`} className="row mb-2">
        <div className="col-md-11">
          <div className="row">
            {table.columns.map((item, j) => {
              return (<div key={`${id}_row_${i}_col_${j}`} className={'col-md-' + (12 / table.columns.length)}>
                <input type="text" className="form-control" value={(curValue[i] && curValue[i][j]) || ''} onChange={ e => setCurValueItem(i, j, e.target.value)}/>
              </div>)
            })}
          </div>
        </div>
        <div className="col-md-1">
          <button type="button" className="btn btn-sm btn-block btn-danger" onClick={ e => { removCurValueItem(i); }}>&times;</button>
        </div>
      </div>
    )
  }
  return (
    <div className="form-group" hidden={!show}>
      <label>{title}</label>
      <div className="card">
        <div className="card-body">
          <div className="row mb-3">
            <div className="col-md-11">
              <div className="row">
                {table.columns.map((item, i) => {
                  return <div key={`${id}_header_${item.id}`} className={'col-md-' + (12 / table.columns.length)}><strong>{item.title}</strong></div>
                })}
              </div>
            </div>
            <div className="col-md-1"></div>
          </div>
          {rows}
          {Array.isArray(value) && value.map((valueItem, i) => {
            return <input key={`${id}_value_${i}`} type="hidden" data-id={`${id}[${i}]`} name={`${name}[n${i}][VALUE]`} value={valueItem}/>
          })}
        </div>
        <div className="card-footer">
          <button type="button" className="btn btn-primary btn-sm" onClick={ e => {setRowsCount(rowsCount+1);}}>Добавить</button>
        </div>
      </div>
    </div>
  );
>>>>>>> e0a0eba79 (init)
}